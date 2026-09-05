<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementParticipation extends Model
{
    protected $fillable = [
        'announcement_id', 'user_id', 'scanned_by', 'points_awarded', 'scanned_at',
    ];

    protected function casts(): array
    {
        return ['scanned_at' => 'datetime'];
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
