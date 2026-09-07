<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\EventRafflePrize;
use App\Models\PointTransaction;
use App\Models\Prize;
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
                'prize_type' => $winner->prize->prize_type_label,
                'points_awarded' => $winner->prize->isPointsPrize() ? $winner->prize->points_amount : 0,
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
            ...$this->prizeFields($request),
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
        $prize->update($this->prizeFields($request));

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
                'prize_type' => $winner->prize->prize_type_label,
                'points_awarded' => $winner->prize->isPointsPrize() ? $winner->prize->points_amount : 0,
                'drawn_at' => $winner->drawn_at->toISOString(),
            ]);
        }

        return back()->with('success', "Raffle winner: {$winner->winner_name_snapshot}.");
    }

    private function validatePrizeRequest(Request $request): void
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prize_type' => ['required', 'in:' . implode(',', array_diff(array_keys(Prize::TYPES), ['none']))],
            'points_amount' => ['required_if:prize_type,points', 'nullable', 'integer', 'min:1', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);
    }

    private function prizeFields(Request $request): array
    {
        $data = $request->only('name', 'description', 'quantity', 'prize_type', 'points_amount');
        $data['type'] = Prize::TYPES[$data['prize_type']]['label'];
        $data['points_amount'] = $data['prize_type'] === 'points' ? (int) $data['points_amount'] : null;

        return $data;
    }
}