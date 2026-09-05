<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'actor_unique_id', 'action', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'ip_address', 'duration_ms',
    ];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array', 'duration_ms' => 'integer'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    /** "Updated by admin (Z2-26000000001) on Aug 14, 2026 3:20 PM" */
    public function getSummaryAttribute(): string
    {
        $who = $this->user?->full_name ?? 'System';
        $id  = $this->actor_unique_id ? " ({$this->actor_unique_id})" : '';

        return ucfirst($this->action) . " by {$who}{$id} on " . $this->created_at->format('M j, Y g:i A');
    }
}

//new file 08/17/2026