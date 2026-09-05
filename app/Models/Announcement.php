<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use Auditable;

    /** Scanning opens this many hours before the event starts. */
    public const SCAN_WINDOW_HOURS = 2;

    public const AUDIENCES = [
        'public'       => 'Public (all residents)',
        'youth'        => 'Youth (15-30 years old)',
        'senior'       => 'Senior citizens (60+)',
        'family_heads' => 'Family heads',
        'officials'    => 'Barangay officials',
    ];

    protected $fillable = [
        'title', 'category', 'body', 'is_featured',
        'is_event', 'allow_guest_scanning', 'raffle_enabled', 'event_start_at', 'event_end_at', 'rsvp_due_at',
        'audiences', 'base_points', 'weight_points',
        'participation_points',
        'qr_token', 'qr_expires_at',
        'venue_name', 'venue_lat', 'venue_lng', 'geofence_radius',
        'banner_path', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_featured'    => 'boolean',
            'is_event'       => 'boolean',
            'allow_guest_scanning' => 'boolean',
            'raffle_enabled' => 'boolean',
            'event_start_at' => 'datetime',
            'event_end_at'   => 'datetime',
            'rsvp_due_at'    => 'datetime',
            'qr_expires_at'  => 'datetime',
            'audiences'      => 'array',
            'weight_points'  => 'float',
            'participation_points' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------
     | QR lifecycle
     * ------------------------------------------------------------------*/

    /** Called whenever an event is saved: token stays, expiry follows the schedule. */
    public function refreshQrToken(): void
    {
        $this->qr_token     = $this->qr_token ?: (string) Str::uuid();
        $this->qr_expires_at = $this->event_end_at ?? $this->event_start_at?->copy()->addHours(4);
    }

    public function qrPayload(): string
    {
        return url("/attendance/scan/{$this->qr_token}");
    }

    public function qrIsExpired(): bool
    {
        return $this->qr_expires_at !== null && now()->greaterThan($this->qr_expires_at);
    }

    /* ------------------------------------------------------------------
     | Attendance window
     * ------------------------------------------------------------------*/

    public function scanOpensAt(): ?Carbon
    {
        return $this->event_start_at?->copy()->subHours(self::SCAN_WINDOW_HOURS);
    }

    public function scanClosesAt(): ?Carbon
    {
        return $this->event_end_at ?? $this->event_start_at?->copy()->addHours(4);
    }

    public function scanningIsOpen(?Carbon $at = null): bool
    {
        $at = $at ?: now();

        return $this->is_event
            && $this->scanOpensAt() !== null
            && $at->betweenIncluded($this->scanOpensAt(), $this->scanClosesAt());
    }

    public function eventStatus(): ?string
    {
        if (! $this->is_event || ! $this->event_start_at) {
            return null;
        }

        if (now()->lessThan($this->scanOpensAt())) {
            return 'soon';
        }

        return $this->scanningIsOpen() ? 'open' : 'closed';
    }

    public function isEarlyScan(?Carbon $at = null): bool
    {
        $at = $at ?: now();

        return $this->event_start_at !== null && $at->lessThan($this->event_start_at);
    }

    /* ------------------------------------------------------------------
     | Survey / RSVP window
     * ------------------------------------------------------------------*/

    public function surveyIsOpen(): bool
    {
        return $this->is_event
            && $this->rsvp_due_at !== null
            && now()->lessThanOrEqualTo($this->rsvp_due_at);
    }

    /* ------------------------------------------------------------------
     | Audience
     * ------------------------------------------------------------------*/

    /** Query of every resident this event is addressed to. */
    public function audienceQuery(): Builder
    {
        $keys = $this->audiences ?: ['public'];

        if (in_array('public', $keys, true)) {
            return User::query();
        }

        return User::where(function (Builder $q) use ($keys) {
            foreach ($keys as $key) {
                match ($key) {
                    'youth'        => $q->orWhere(fn ($s) => $s->youth()),
                    'senior'       => $q->orWhere(fn ($s) => $s->seniors()),
                    'family_heads' => $q->orWhere(fn ($s) => $s->familyHeads()),
                    'officials'    => $q->orWhere('role', 'official'),
                    default        => null,
                };
            }
        });
    }

    public function audienceLabels(): array
    {
        return collect($this->audiences ?: [])
            ->map(fn ($k) => self::AUDIENCES[$k] ?? $k)
            ->all();
    }

        public function getBannerUrlAttribute(): ?string
    {
            if (! $this->banner_path) {
                return null;
            }

            return asset('storage/' . ltrim($this->banner_path, '/'));
    }

    /* ------------------------------------------------------------------
     | Relationships + scopes
     * ------------------------------------------------------------------*/

    public function rsvps()
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function raffleEntries()
    {
        return $this->hasMany(EventRaffleEntry::class);
    }

    public function participations()
    {
        return $this->hasMany(AnnouncementParticipation::class);
    }

    public function isParticipationActivity(): bool
    {
        return in_array($this->category, ['ice_breaker', 'q_and_a', 'game', 'intermission'], true);
    }

    public function reminders()
    {
        return $this->hasMany(EventReminder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeEvents(Builder $q): Builder
    {
        return $q->where('is_event', true);
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('is_event', true)->where('event_start_at', '>=', now()->startOfDay());
    }
}

//Edited 08/17/2026