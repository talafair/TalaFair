<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class GameRun extends Model
{
    use Auditable;

    protected $fillable = ['user_id', 'game', 'played_on', 'stage_scores', 'total_score'];

    protected function casts(): array
    {
        return ['played_on' => 'date', 'stage_scores' => 'array'];
    }

    public function user() { return $this->belongsTo(User::class); }
}
