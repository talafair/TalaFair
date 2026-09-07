<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRafflePrize extends Model
{
    protected $fillable = ['announcement_id', 'name', 'type', 'description', 'quantity', 'sort_order', 'created_by'];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'sort_order' => 'integer'];
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function winners()
    {
        return $this->hasMany(EventRaffleWinner::class, 'event_raffle_prize_id')->orderBy('draw_sequence');
    }

    public function remainingSlots(): int
    {
        return max(0, $this->quantity - $this->winners()->count());
    }
}