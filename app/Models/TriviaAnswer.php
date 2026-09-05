<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class TriviaAnswer extends Model
{
    use Auditable;

    public $timestamps = false;

    protected $fillable = ['trivia_theme_id', 'user_id', 'question_index', 'is_correct', 'rank', 'points_awarded', 'answered_at'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean', 'answered_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function theme() { return $this->belongsTo(TriviaTheme::class, 'trivia_theme_id'); }
}
