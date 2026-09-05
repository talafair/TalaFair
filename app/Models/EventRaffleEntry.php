<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRaffleEntry extends Model
{
    protected $fillable = ['announcement_id', 'user_id', 'is_early', 'weight', 'selected_at'];

    protected function casts(): array
    {
        return [
            'is_early' => 'boolean',
            'weight' => 'float',
            'selected_at' => 'datetime',
        ];
    }

    public function announcement() { return $this->belongsTo(Announcement::class); }
    public function user() { return $this->belongsTo(User::class); }
}
