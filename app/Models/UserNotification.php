<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = ['user_id', 'announcement_id', 'survey_id', 'title', 'body', 'read_at', 'created_by'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}

//new file 08/17/2026