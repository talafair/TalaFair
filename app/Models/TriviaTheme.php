<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class TriviaTheme extends Model
{
    use Auditable;

    protected $fillable = ['title', 'base_points', 'due_at', 'questions', 'created_by'];

    protected function casts(): array
    {
        return ['questions' => 'array', 'due_at' => 'datetime'];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
