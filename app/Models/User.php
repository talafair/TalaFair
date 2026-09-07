<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    public const CATEGORIES = [
        'resident' => 'Resident',
        'guest' => 'Guest',
        'official' => 'Official',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            foreach ([
                'name', 'first_name', 'middle_name', 'last_name', 'suffix', 'gender_other',
                'school', 'occupation', 'street', 'barangay', 'city', 'province',
                'country', 'head_of_family_name',
            ] as $field) {
                if ($user->getAttribute($field) !== null) {
                    $value = trim((string) $user->getAttribute($field));
                    $user->setAttribute($field, $value === '' ? null : Str::title($value));
                }
            }
        });
    }

    protected $fillable = [
        'name',
        'first_name', 'middle_name', 'last_name', 'suffix',
        'username', 'email', 'password', 'role', 'official_group', 'official_position', 'is_verified', 'points',
        'gender', 'gender_other', 'birthdate', 'contact_number',
        'is_student', 'school', 'occupation',
        'house_no', 'street', 'zone', 'barangay', 'city', 'province', 'country', 'postal_code',
        'is_head_of_family', 'head_of_family_name', 'head_of_family_id',
        'unique_id', 'avatar_path',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'birthdate'         => 'date',
            'is_head_of_family' => 'boolean',
            'is_verified'      => 'boolean',
            'is_student'       => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------
     | Accessors
     * ------------------------------------------------------------------*/

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])->filter()->implode(' ')) ?: (string) $this->name;
    }

    /** "Dela Cruz, Juan P." — useful for lists and the ID card back. */
    public function getListNameAttribute(): string
    {
        $mi = $this->middle_name ? ' ' . strtoupper(substr($this->middle_name, 0, 1)) . '.' : '';

        return trim("{$this->last_name}, {$this->first_name}{$mi} {$this->suffix}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthdate?->age;   // computed from birthdate, never stored
    }

    public function getGenderLabelAttribute(): ?string
    {
        return $this->gender === 'others'
            ? ($this->gender_other ?: 'Others')
            : ($this->gender ? ucfirst($this->gender) : null);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            trim("{$this->house_no} {$this->street}"),
            $this->zone ? "Zone {$this->zone}" : null,
            "Barangay {$this->barangay}",
            $this->city,
            $this->province,
            $this->country,
            $this->postal_code,
        ])->filter()->implode(', ');
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar_path
                ? Storage::disk(config('filesystems.uploads_disk', 'public'))->url($this->avatar_path)
            : asset('images/default-avatar.svg');
    }

    public function isOfficial(): bool
    {
        return $this->role === 'official' && $this->official_group !== 'personnel';
    }

    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    /* ------------------------------------------------------------------
     | Audience scopes (used to target announcements)
     * ------------------------------------------------------------------*/

    public function scopeYouth(Builder $q): Builder            // 15 - 30 years old
    {
        return $q->whereNotNull('birthdate')
                 ->whereDate('birthdate', '<=', now()->subYears(15))
                 ->whereDate('birthdate', '>',  now()->subYears(31));
    }

    public function scopeSeniors(Builder $q): Builder          // 60 +
    {
        return $q->whereNotNull('birthdate')
                 ->whereDate('birthdate', '<=', now()->subYears(60));
    }

    public function scopeFamilyHeads(Builder $q): Builder
    {
        return $q->where('is_head_of_family', true);
    }

    /** Does this resident fall inside any of the given audience keys? */
    public function belongsToAudience(array $audiences): bool
    {
        if (empty($audiences) || in_array('public', $audiences, true)) {
            return true;
        }

        $age = $this->age;

        foreach ($audiences as $key) {
            $match = match ($key) {
                'youth'         => $age !== null && $age >= 15 && $age <= 30,
                'senior'        => $age !== null && $age >= 60,
                'family_heads'  => (bool) $this->is_head_of_family,
                'officials'     => $this->isOfficial(),
                default         => false,
            };

            if ($match) {
                return true;
            }
        }

        return false;
    }

    public function audienceQueryFor(array $audiences): Builder
    {
        if (empty($audiences) || in_array('public', $audiences, true)) {
            return static::query();
        }

        return static::where(function (Builder $query) use ($audiences) {
            foreach ($audiences as $audience) {
                match ($audience) {
                    'youth' => $query->orWhere(fn ($scope) => $scope->youth()),
                    'senior' => $query->orWhere(fn ($scope) => $scope->seniors()),
                    'family_heads' => $query->orWhere(fn ($scope) => $scope->familyHeads()),
                    'officials' => $query->orWhere('role', 'official'),
                    default => null,
                };
            }
        });
    }

        /* ------------------------------------------------------------------
     | Relationships
     * ------------------------------------------------------------------*/

    public function headOfFamily()
    {
        return $this->belongsTo(User::class, 'head_of_family_id');
    }

    public function familyMembers()
    {
        return $this->hasMany(User::class, 'head_of_family_id');
    }

    /** Spin-the-wheel history. AccountController and the home page use this. */
    public function spins()
    {
        return $this->hasMany(SpinHistory::class);
    }

    public function rsvps()
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function raffleEntry()
    {
        return $this->hasOne(RaffleEntry::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class)->withTimestamps()->withPivot('awarded_by', 'award_rank');
    }

    /**
     * TalaFair's own in-app inbox (user_notifications table).
     * Do NOT name this notifications() — that name belongs to the Notifiable trait.
     */
    public function appNotifications()
    {
        return $this->hasMany(UserNotification::class)->latest();
    }

    public function unreadAppNotifications()
    {
        return $this->appNotifications()->whereNull('read_at');
    }
}