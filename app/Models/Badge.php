<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Badge extends Model
{
    use Auditable;

    protected $fillable = [
        'name', 'description', 'image_path', 'points_required', 'category', 'rarity', 'award_method',
        'condition_key', 'limited_total', 'announcement_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'limited_total' => 'integer',
        ];
    }

        public function getImageUrlAttribute(): ?string
    {
            return $this->image_path
                ? Storage::disk(config('filesystems.uploads_disk', 'public'))->url($this->image_path)
                : null;
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('awarded_by', 'award_rank');
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}

