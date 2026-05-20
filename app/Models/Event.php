<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_type_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'capacity',
        'price',
        'status',
        'confirmation_deadline',
        'min_attendees',
        'attendance_type',
        'max_guests_per_member',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'confirmation_deadline' => 'date',
        'capacity' => 'integer',
        'min_attendees' => 'integer',
        'max_guests_per_member' => 'integer',
        'price' => 'decimal:2',
    ];

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function confirmedAttendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class)->where('status', 'CONFIRMED');
    }

    public function tentativeAttendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class)->where('status', 'TENTATIVE');
    }

    public function getTotalGuestsAttribute(): int
    {
        return $this->attendees()->sum('guests_count');
    }

    public function getTotalAttendeesAttribute(): int
    {
        return $this->attendees()->count();
    }

    public function getConfirmedCountAttribute(): int
    {
        return $this->attendees()->where('status', 'CONFIRMED')->count();
    }

    public function getTentativeCountAttribute(): int
    {
        return $this->attendees()->where('status', 'TENTATIVE')->count();
    }

    public function getTotalRevenueAttribute(): float
    {
        return $this->attendees()->where('status', 'CONFIRMED')->sum('amount_paid');
    }

    public function getAvailableSlotsAttribute(): int
    {
        if (!$this->capacity) return PHP_INT_MAX;
        return max(0, $this->capacity - $this->confirmedAttendees()->count());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now()->toDateString())
                    ->where('end_date', '>=', now()->toDateString());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now()->toDateString());
    }
}
