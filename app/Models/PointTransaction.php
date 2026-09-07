<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable = [
        'user_id', 'announcement_id', 'type', 'description',
        'base_points', 'multiplier', 'points_awarded',
    ];

    protected function casts(): array
    {
        return [
            'base_points' => 'integer',
            'multiplier' => 'float',
            'points_awarded' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
