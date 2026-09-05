<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\EventRsvp;
use App\Models\EventSubstitution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RsvpController extends Controller
{
    /** Resident answers the attendance survey. */
    public function store(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless($announcement->is_event, 404);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $isAssignedSubstitute = EventSubstitution::where('announcement_id', $announcement->id)
            ->where('substitute_user_id', $user->id)
            ->exists();

        if (! $user->belongsToAudience($announcement->audiences ?: ['public']) && ! $isAssignedSubstitute) {
            return back()->with('error', 'This survey is for a different audience.');
        }

        if (! $announcement->surveyIsOpen()) {
            return back()->with('error', 'The survey closed on ' .
                $announcement->rsvp_due_at?->format('M j, Y g:i A') . '.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['attending', 'not_attending'])],
            'reason' => ['nullable', 'required_if:status,not_attending', 'string', 'max:500'],
        ], [
            'reason.required_if' => 'Please tell the barangay why you cannot attend.',
        ]);

        EventRsvp::updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $user->id],
            [
                'status'       => $data['status'],
                'reason'       => $data['status'] === 'not_attending' ? $data['reason'] : null,
                'responded_at' => now(),
                'updated_by'   => $user->id,
            ]
        );

        return back()->with('success', $data['status'] === 'attending'
            ? 'You are on the list. See you there.'
            : 'Your response was recorded.');
    }
}

