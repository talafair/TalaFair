<?php

namespace App\Http\Controllers;

use App\Models\Prize;
use Illuminate\Http\Request;

class PrizeController extends Controller
{
    private const MIN_PRIZES = 2;
    private const MAX_PRIZES = 12;

    public function index()
    {
        return view('pages.prizes', [
            'prizes' => Prize::orderBy('id')->get(),
            'maxPrizes' => self::MAX_PRIZES,
        ]);
    }

    public function store(Request $request)
    {
        if (Prize::count() >= self::MAX_PRIZES) {
            return back()->with('error', 'The wheel can hold at most ' . self::MAX_PRIZES . ' prizes.');
        }

        Prize::create($this->validated($request));

        return back()->with('success', 'Prize added to the wheel.');
    }

    public function update(Request $request, Prize $prize)
    {
        $prize->update($this->validated($request));

        return back()->with('success', 'Prize updated.');
    }

    public function destroy(Prize $prize)
    {
        if (Prize::count() <= self::MIN_PRIZES) {
            return back()->with('error', 'The wheel needs at least ' . self::MIN_PRIZES . ' prizes.');
        }

        $prize->delete();

        return back()->with('success', 'Prize removed from the wheel.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'prize_type' => ['required', 'in:' . implode(',', array_keys(Prize::TYPES))],
            'amount' => ['required_if:prize_type,points', 'nullable', 'integer', 'min:1', 'max:1000000'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $data['amount'] = $data['prize_type'] === 'points' ? (int) $data['amount'] : 0;

        return $data;
    }
}
