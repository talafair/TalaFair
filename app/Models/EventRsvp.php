<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class EventRsvp extends Model
{
    use Auditable;

    protected $fillable = [
        'announcement_id', 'user_id', 'status', 'reason', 'responded_at', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function isAttending(): bool
    {
        return $this->status === 'attending';
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

//new file 08/17/2026