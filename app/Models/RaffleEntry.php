<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaffleEntry extends Model
{
    protected $fillable = [
        'user_id',
        'extra_chances',
    ];

    protected function casts(): array
    {
        return ['extra_chances' => 'integer'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}