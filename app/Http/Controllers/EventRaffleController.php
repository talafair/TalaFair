<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\EventRafflePrize;
use App\Services\RaffleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventRaffleController extends Controller
{
    public function live(Announcement $announcement)
    {
        abort_unless($announcement->is_event && $announcement->raffle_enabled, 404);

        return view('pages.event-raffle-live', [
            'announcement' => $announcement,
            'winners' => $announcement->raffleWinners()->with('prize')->get(),
        ]);
    }

    public function state(Announcement $announcement): JsonResponse
    {
        abort_unless($announcement->is_event && $announcement->raffle_enabled, 404);

        return response()->json([
            'winners' => $announcement->raffleWinners()->with('prize')->get()->map(fn ($winner) => [
                'prize' => $winner->prize->name,
                'name' => $winner->public_name,
                'drawn_at' => $winner->drawn_at->toISOString(),
            ]),
        ]);
    }

    public function storePrize(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless($announcement->is_event && $announcement->raffle_enabled, 404);
        $this->validatePrizeRequest($request);

        EventRafflePrize::create([
            ...$request->only('name', 'type', 'description', 'quantity'),
            'announcement_id' => $announcement->id,
            'created_by' => $request->user()->id,
            'sort_order' => $announcement->rafflePrizes()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Raffle prize added.');
    }

    public function updatePrize(Request $request, Announcement $announcement, EventRafflePrize $prize): RedirectResponse
    {
        abort_unless($prize->announcement_id === $announcement->id, 404);
        $this->validatePrizeRequest($request);
        // Only block modification if this specific prize has winners
        abort_if($prize->winners()->exists(), 422, 'This prize cannot be changed because a winner has already been drawn.');
        $prize->update($request->only('name', 'type', 'description', 'quantity'));

        return back()->with('success', 'Raffle prize updated.');
    }

    public function destroyPrize(Announcement $announcement, EventRafflePrize $prize): RedirectResponse
    {
        abort_unless($prize->announcement_id === $announcement->id, 404);
        // Only block deletion if this specific prize has winners
        abort_if($prize->winners()->exists(), 422, 'This prize cannot be deleted because a winner has already been drawn.');
        $prize->delete();

        return back()->with('success', 'Raffle prize removed.');
    }

    public function draw(Request $request, Announcement $announcement, EventRafflePrize $prize): JsonResponse|RedirectResponse
    {
        try {
            $winner = RaffleService::draw($announcement, $prize);
        } catch (\Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }
            throw $exception;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'winner' => $winner->winner_name_snapshot,
                'winner_user_id' => $winner->user_id,
                'prize' => $winner->prize->name,
                'drawn_at' => $winner->drawn_at->toISOString(),
            ]);
        }

        return back()->with('success', "Raffle winner: {$winner->winner_name_snapshot}.");
    }

    private function validatePrizeRequest(Request $request): void
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);
    }
}